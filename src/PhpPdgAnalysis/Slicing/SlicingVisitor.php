<?php

namespace PhpPdgAnalysis\Slicing;

use PhpParser\Node;
use PhpParser\NodeTraverserInterface;
use PhpParser\NodeVisitorAbstract;

class SlicingVisitor extends NodeVisitorAbstract {
	private $match_lines;
	private $subnodes_in_array;

	public function __construct($match_lines) {
		$this->match_lines = $match_lines;
		$this->subnodes_in_array = new \SplObjectStorage();
	}

	public function beforeTraverse(array $nodes) {
		foreach ($nodes as $node) {
			$this->subnodes_in_array->attach($node);
		}
	}

	public function enterNode(Node $node) {
		foreach ($node->getSubNodeNames() as $name) {
			if (is_array($node->$name) === true) {
				foreach ($node->$name as $subnode) {
					if (is_object($subnode) && $subnode instanceof Node) {
						$this->subnodes_in_array->attach($subnode);    // track nodes that can be removed from arrays
					}
				}
			}
		}
	}

	public function leaveNode(Node $node) {
		if ($this->subnodes_in_array->contains($node) === true) {
			if ($this->nodeMatches($node) === false) {
				return NodeTraverserInterface::REMOVE_NODE;
			}
			$this->subnodes_in_array->detach($node);
		}
		return null;
	}

	private function nodeMatches(Node $node) {
		if (isset($this->match_lines[$node->getLine()]) === true) {
			return true;
		}
		foreach ($node->getSubNodeNames() as $name) {
			if (is_array($node->$name) === true) {
				foreach ($node->$name as $subnode) {
					if (is_object($subnode) === true && $subnode instanceof Node) {
						return true;
					}
				}
			} else if (is_object($node->$name) === true && $node->$name instanceof Node) {
				if ($this->nodeMatches($node->$name) === true) {
					return true;
				}
			}
		}
		return false;
	}
}